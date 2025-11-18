import { useState, useEffect } from "react";
import { useParams, Link } from "react-router";
import TaskService from "@/services/TaskService";
import CommentService from "@/services/CommentService";
import type { Task, Comment } from "@/types/API";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import Spinner from "../Spinner";
import { toast } from "sonner";

export default function TaskDetailPage() {
  const { taskId } = useParams<{ taskId: string }>();
  const [task, setTask] = useState<Task | null>(null);
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [commentText, setCommentText] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (taskId) {
      loadTask();
      loadComments();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [taskId]);

  const loadTask = async () => {
    if (!taskId) return;

    try {
      const response = await TaskService.getTask(taskId);
      if (response.data.success) {
        setTask(response.data.data as Task);
      }
    } catch {
      toast.error("Failed to load task");
    }
  };

  const loadComments = async () => {
    if (!taskId) return;

    try {
      setLoading(true);
      const response = await CommentService.getTaskComments(taskId);
      if (response.data.success) {
        setComments(response.data.data as Comment[]);
      }
    } catch {
      toast.error("Failed to load comments");
    } finally {
      setLoading(false);
    }
  };

  const handleAddComment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!taskId || !commentText.trim()) return;

    try {
      setIsSubmitting(true);
      const response = await CommentService.createComment(taskId, {
        content: commentText,
      });
      if (response.data.success) {
        toast.success("Comment added successfully!");
        setCommentText("");
        loadComments();
      }
    } catch {
      toast.error("Failed to add comment");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleDeleteComment = async (commentId: string) => {
    if (!confirm("Are you sure you want to delete this comment?")) return;

    try {
      await CommentService.deleteComment(commentId);
      setComments(comments.filter((c) => c.id !== commentId));
      toast.success("Comment deleted successfully");
    } catch {
      toast.error("Failed to delete comment");
    }
  };

  if (loading || !task) return <Spinner />;

  return (
    <div className="task-detail-page p-8">
      <div className="page-header mb-6">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-3xl font-bold text-foreground mb-2">
              {task.title}
            </h1>
            <div className="flex gap-2">
              <span className="badge bg-primary-100 text-primary-700">
                {task.status}
              </span>
              <span className="badge bg-secondary-100 text-secondary-700">
                {task.priority}
              </span>
            </div>
          </div>
          <Link to={`/dashboard/tasks/${taskId}/edit`}>
            <Button>Edit Task</Button>
          </Link>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <div className="bg-card border border-border rounded-lg p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Description</h2>
            <p className="text-muted-foreground whitespace-pre-wrap">
              {task.description}
            </p>
          </div>

          <div className="bg-card border border-border rounded-lg p-6">
            <h2 className="text-xl font-semibold mb-4">
              Comments ({comments.length})
            </h2>

            <form onSubmit={handleAddComment} className="mb-6">
              <div className="form-group">
                <Label htmlFor="comment">Add a comment</Label>
                <Textarea
                  id="comment"
                  value={commentText}
                  onChange={(e) => setCommentText(e.target.value)}
                  placeholder="Write your comment here..."
                  rows={3}
                  disabled={isSubmitting}
                />
              </div>
              <Button
                type="submit"
                disabled={isSubmitting || !commentText.trim()}
              >
                {isSubmitting ? "Adding..." : "Add Comment"}
              </Button>
            </form>

            <div className="space-y-4">
              {comments.length === 0 ? (
                <p className="text-muted-foreground text-center py-4">
                  No comments yet. Be the first to comment!
                </p>
              ) : (
                comments.map((comment) => (
                  <div
                    key={comment.id}
                    className="border border-border rounded-lg p-4"
                  >
                    <div className="flex justify-between items-start mb-2">
                      <div className="text-sm text-muted-foreground">
                        User #{comment.user_id}
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDeleteComment(comment.id)}
                      >
                        Delete
                      </Button>
                    </div>
                    <p className="text-foreground whitespace-pre-wrap">
                      {comment.content}
                    </p>
                    {comment.file_path && (
                      <div className="mt-2">
                        <a
                          href={comment.file_path}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-primary hover:underline text-sm"
                        >
                          View attachment
                        </a>
                      </div>
                    )}
                  </div>
                ))
              )}
            </div>
          </div>
        </div>

        <div className="lg:col-span-1">
          <div className="bg-card border border-border rounded-lg p-6">
            <h2 className="text-lg font-semibold mb-4">Task Details</h2>
            <dl className="space-y-3">
              <div>
                <dt className="text-sm text-muted-foreground">Project ID</dt>
                <dd className="text-foreground font-medium">
                  {task.project_id}
                </dd>
              </div>
              {task.assigned_to && (
                <div>
                  <dt className="text-sm text-muted-foreground">Assigned To</dt>
                  <dd className="text-foreground font-medium">
                    User #{task.assigned_to}
                  </dd>
                </div>
              )}
              {task.deadline && (
                <div>
                  <dt className="text-sm text-muted-foreground">Deadline</dt>
                  <dd className="text-foreground font-medium">
                    {new Date(task.deadline).toLocaleDateString()}
                  </dd>
                </div>
              )}
              <div>
                <dt className="text-sm text-muted-foreground">Created</dt>
                <dd className="text-foreground font-medium">
                  {new Date(task.created_at).toLocaleDateString()}
                </dd>
              </div>
              <div>
                <dt className="text-sm text-muted-foreground">Last Updated</dt>
                <dd className="text-foreground font-medium">
                  {new Date(task.updated_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  );
}
